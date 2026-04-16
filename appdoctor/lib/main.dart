import 'package:flutter/material.dart';
import 'theme/app_theme.dart';
import 'screens/role_select_screen.dart';
import 'screens/auth_screen.dart';
import 'screens/main_shell.dart';

void main() {
  runApp(const AppDoctorApp());
}

class AppDoctorApp extends StatelessWidget {
  const AppDoctorApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Medica - Turno Médico',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.primary,
          primary: AppColors.primary,
          surface: AppColors.surface,
          brightness: Brightness.light,
        ),
        scaffoldBackgroundColor: AppColors.background,
        useMaterial3: true,
        cardTheme: CardTheme(
          color: AppColors.cardBackground,
          elevation: 2,
          shadowColor: Colors.black12,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: AppColors.white,
            elevation: 0,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        ),
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const MainShell(),
        '/role': (context) => const RoleSelectScreen(),
        '/auth': (context) {
          final isDoctor = ModalRoute.of(context)?.settings.arguments as bool? ?? false;
          return AuthScreen(isDoctor: isDoctor);
        },
      },
    );
  }
}
