import 'package:flutter_test/flutter_test.dart';
import 'package:appdoctor/main.dart';

void main() {
  testWidgets('App shows home with doctors first', (WidgetTester tester) async {
    await tester.pumpWidget(const AppDoctorApp());
    await tester.pumpAndSettle();

    expect(find.text('Buscar médico'), findsOneWidget);
    expect(find.text('Todos los médicos'), findsOneWidget);
  });
}
